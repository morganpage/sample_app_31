module ApplicationHelper
  def title
    base_title = "Plato Evolved"
    if @title.nil?
      base_title
    else
      "#{base_title} | #{@title}"
    end
  end

  def sectiontitle
    base_header = "Plato Evolved"
    if @title.nil?
      base_header
    else
      "#{@title}"
    end
  end

end
